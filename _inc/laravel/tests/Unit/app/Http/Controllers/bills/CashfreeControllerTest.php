<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Coupon, Invoice, Plan, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, DB, Http, Log};
use Tests\TestCase;

class CashfreeControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Http::fake(); // Avoid actual API requests
		Log::spy();
		DB::shouldReceive('beginTransaction')->andReturnTrue();
		DB::shouldReceive('commit')->andReturnTrue();
		DB::shouldReceive('rollBack')->andReturnTrue();
	}

	/**
	 ** @test
	 **
	 ** Store a Cashfree payment request and redirect the user
	 ** to the returned payment link from the Cashfree API.
	 **/
	public function test_cashfree_payment_store_redirects_to_payment_link()
	{
		$user = User::factory()->create();
		$plan = Plan::factory()->create(['price' => 200]);
		$this->actingAs($user);

		$coupon = Coupon::factory()->create([
			'discount' => 20,
			'code'     => 'SAVE20',
			'limit'    => 10,
		]);

		Http::fake([
			'*' => Http::response(['payment_link' => 'https://cashfree.com/pay'], 200),
		]);

		$response = $this->post(route('cashfreePayment.store'), [
			'plan_id' => Crypt::encrypt($plan->id),
			'coupon'  => $coupon->code,
		]);

		$response->assertRedirect('https://cashfree.com/pay');
	}

	/**
	 ** @test
	 **
	 ** After successful Cashfree payment, activate the selected plan
	 ** and attach the used coupon to the user.
	 **/
	public function test_cashfree_payment_success_activates_plan_and_attaches_coupon()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$plan = Plan::factory()->create();
		$coupon = Coupon::factory()->create(['code' => 'FREE100']);

		Http::fake([
			'*' => Http::sequence()
				->push(['cf_payment_id' => '1234', 'order_id' => 'ORDER01'])
				->push(['payment_status' => 'SUCCESS']),
		]);

		$response = $this->get(route('cashfreePayment.success', [
			'order_id' => 'ORDER01',
			'plan_id'  => $plan->id,
			'amount'   => 100,
			'coupon'   => $coupon->code,
		]));

		$response->assertRedirect(route('plans.index'));
		$this->assertDatabaseHas('orders', ['plan_id' => $plan->id]);
		$this->assertDatabaseHas('user_coupons', ['coupon' => $coupon->id]);
	}

	/**
	 ** @test
	 **
	 ** Initiate an Invoice payment via Cashfree and redirect
	 ** the user to the payment link provided by the API.
	 **/
	public function test_invoice_pay_with_cashfree_redirects_to_payment_link()
	{
		$user = User::factory()->create();
		$invoice = Invoice::factory()->create(['created_by' => $user?->id]);
		$this->actingAs($user);

		Http::fake([
			'*' => Http::response(['payment_link' => 'https://cashfree.com/pay'], 200),
		]);

		$response = $this->post(route('invoice.cashfree.store'), [
			'invoice_id' => Crypt::encrypt($invoice->id),
			'amount'     => 120,
		]);

		$response->assertRedirect('https://cashfree.com/pay');
	}

	/**
	 ** @test
	 **
	 ** Poll Cashfree for invoice payment status, then record
	 ** the payment in the database once successful.
	 **/
	public function test_get_invoice_payment_status_records_payment()
	{
		$user = User::factory()->create();
		$invoice = Invoice::factory()->create(['created_by' => $user?->id]);
		$this->actingAs($user);

		Http::fake([
			'*' => Http::sequence()
				->push(['cf_payment_id' => 'ABC123', 'order_id' => 'ORD789'])
				->push(['payment_status' => 'SUCCESS']),
		]);

		$response = $this->get(route('invoice.cashfree.status', [
			'order_id'   => 'ORD789',
			'invoice_id' => $invoice->id,
			'amount'     => 120,
		]));

		$response->assertRedirect();
		$this->assertDatabaseHas('invoice_payments', [
			'invoice_id'   => $invoice->id,
			'amount'       => 120,
			'payment_type' => 'Cashfree',
		]);
	}

	/**
	 ** @test
	 **
	 ** If a coupon covers the full plan price, skip Cashfree
	 ** and activate the plan immediately with zero cost.
	 **/
	public function test_cashfree_payment_store_activates_plan_if_coupon_covers_all()
	{
		$user = User::factory()->create();
		$plan = Plan::factory()->create(['price' => 100]);
		$coupon = Coupon::factory()->create([
			'code'     => 'FULL100',
			'discount' => 100,
			'limit'    => 1,
		]);
		$this->actingAs($user);

		$response = $this->post(route('cashfreePayment.store'), [
			'plan_id' => Crypt::encrypt($plan->id),
			'coupon'  => 'FULL100',
		]);

		$response->assertRedirect(route('plans.index'));
		$this->assertDatabaseHas('orders', [
			'plan_id' => $plan->id,
			'price'   => 0,
		]);
	}
}
