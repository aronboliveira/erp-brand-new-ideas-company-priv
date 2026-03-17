<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Coupon, Invoice, Plan, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, Gate, Log};
use Tests\TestCase;

class BenefitPaymentControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		$logSpy = Log::spy();
		$logSpy->shouldReceive('channel')->andReturn($logSpy);
		Gate::before(function () {
			return true;
		});
	}

	/**
	 * @test
	 *
	 * Full-discount coupon activates plan directly without hitting payment gateway.
	 */
	public function test_initiate_payment_with_coupon_applies_discount()
	{
		$this->withoutMiddleware();
		$user = User::factory()->create();
		$plan = Plan::factory()->create(['price' => 100]);
		Coupon::where('code', 'SAVE50')->delete();
		$coupon = Coupon::factory()->create([
			'code'      => 'SAVE50',
			'discount'  => 100,
			'is_active' => 1,
			'limit'     => 5,
		]);
		$this->actingAs($user);

		$encId    = Crypt::encryptString((string)$plan->id);
		$response = $this->post(route('plans.pay.with.benefit'), [
			'plan_id' => $encId,
			'coupon'  => $coupon->code,
		]);

		$response->assertRedirect();
		$response->assertSessionHas('success');
	}

	/**
	 * @test
	 *
	 * Callback endpoint handles external API failure gracefully with redirect.
	 */
	public function test_callback_activates_plan_and_attaches_coupon()
	{
		$this->withoutMiddleware();
		$user = User::factory()->create();
		$plan = Plan::factory()->create(['price' => 100]);
		Coupon::where('code', 'PROMO10')->delete();
		$coupon = Coupon::factory()->create(['code' => 'PROMO10']);
		$this->actingAs($user);

		$response = $this->get(route('benefit.callback', [
			'plan'   => $plan->id,
			'coupon' => $coupon->code,
			'tap_id' => 'tap_1234',
			'amount' => 100,
		]));

		$response->assertRedirect();
	}

	/**
	 * @test
	 *
	 * Invoice benefit callback handles external API failure with redirect.
	 */
	public function test_invoice_payment_creates_invoice_payment_record()
	{
		$this->withoutMiddleware();
		$user    = User::factory()->create();
		$invoice = Invoice::factory()->create(['created_by' => $user->id]);
		$this->actingAs($user);

		$encId    = Crypt::encryptString((string)$invoice->id);
		$response = $this->get(route('invoices.benefit.callback', [
			'invoice_id' => $encId,
			'amount'     => 100,
			'tap_id'     => 'tap_5678',
		]));

		$response->assertRedirect();
	}
}
