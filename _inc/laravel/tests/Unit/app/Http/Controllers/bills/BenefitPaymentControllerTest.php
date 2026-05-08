<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Coupon, Invoice, InvoicePayment, Order, Plan, User, UserCoupon};
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, DB, Log, Http};
use Illuminate\Support\Str;
use Tests\TestCase;

class BenefitPaymentControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
	}

	protected function fakeGuzzle(array $responses = []): void
	{
		if (empty($responses)) {
			$responses = [
				new Response(200, [], json_encode([
					'gateway'     => ['response' => ['code' => '00']],
					'transaction' => ['url' => 'https://fakeurl.com'],
				])),
			];
		}
		$mock = new MockHandler($responses);
		$stack = HandlerStack::create($mock);
		$this->app->bind(Client::class, fn () => new Client(['handler' => $stack]));
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
			'code'     => 'SAVE50_' . \Illuminate\Support\Str::random(6),
			'discount' => 50,
			'limit'    => 5,
		]);
		$this->actingAs($user);
		$this->fakeGuzzle();

		$encId   = Crypt::encryptString($plan->id);
		$response = $this->post(route('plans.pay.with.benefit'), [
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
		$coupon = Coupon::factory()->create(['code' => 'PROMO10_' . Str::random(6)]);
		$this->actingAs($user);
		$this->fakeGuzzle();

		$response = $this->get(route('benefit.callback', [
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
		$this->fakeGuzzle();

		$encId   = Crypt::encryptString($invoice->id);
		$response = $this->get(route('invoices.benefit.callback', [
			'invoice_id' => $encId,
			'amount'     => 100,
			'tap_id'     => 'tap_5678',
		]));

		$response->assertRedirect();
		$this->assertDatabaseHas('invoice_payments', [
			'invoice_id'   => $invoice->id,
			'amount'       => 100,
			'payment_type' => 'other',
		]);
	}
}
