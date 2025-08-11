<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{Coupon, Order, Plan, User, UserCoupon, Invoice, InvoiceBankTransfer, InvoicePayment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, DB, Log};
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BankTransferPaymentControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
		DB::shouldReceive('beginTransaction')->andReturnTrue();
		DB::shouldReceive('commit')->andReturnTrue();
		DB::shouldReceive('rollBack')->andReturnTrue();
	}

	/**
	 ** @test
	 **
	 ** A user can purchase a plan via bank transfer with a valid coupon;
	 ** an Order and UserCoupon record should be created, and the user
	 ** should be redirected back to the plans index.
	 **/
	public function test_plan_payment_with_coupon_creates_order()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('create order');

		$plan  = Plan::factory()->create(['price' => 100]);
		$coupon = Coupon::factory()->create([
			'code'    => 'DISCOUNT',
			'discount' => 50,
			'limit'   => 10
		]);
		$encId = Crypt::encryptString($plan->id);

		$file = UploadedFile::fake()->create('receipt.pdf');

		$response = $this->post(route('plan.bank'), [
			'plan_id'         => $encId,
			'coupon'          => $coupon->code,
			'payment_receipt' => $file,
		]);

		$response->assertRedirect(route('plans.index'));
		$this->assertDatabaseHas('orders', [
			'plan_id'        => $plan->id,
			'payment_status' => 'Pending',
			'user_id'        => $user?->id,
		]);
		$this->assertDatabaseHas('user_coupons', [
			'user'   => $user?->id,
			'coupon' => $coupon->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Changing an order’s status to Approval should mark it as Approved
	 ** and redirect to the orders index.
	 **/
	public function test_change_status_approves_order_and_assigns_plan()
	{
		$user = User::factory()->create();
		$plan = Plan::factory()->create();
		$order = Order::factory()->create([
			'user_id' => $user?->id,
			'plan_id' => $plan->id,
		]);

		$this->actingAs(User::factory()->create());

		$response = $this->post(route('order.changeStatus', $order->id), [
			'order_id' => $order->id,
			'status'   => 'Approval',
		]);

		$response->assertRedirect(route('order.index'));
		$this->assertEquals('Approved', $order->fresh()->payment_status);
	}

	/**
	 ** @test
	 **
	 ** When approving a bank transfer for an invoice, an InvoicePayment
	 ** record should be created and the user redirected.
	 **/
	public function test_invoice_payment_approval_creates_invoice_payment()
	{
		$user    = User::factory()->create();
		$invoice = Invoice::factory()->create(['created_by' => $user?->id]);
		$transfer = InvoiceBankTransfer::factory()->create([
			'invoice_id' => $invoice->id,
			'amount'     => 300,
			'status'     => 'Pending',
		]);

		$this->actingAs($user);

		$response = $this->post(route('invoice.changeStatus', $invoice->id), [
			'order_id' => $transfer->order_id,
			'status'   => 'Approval',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('invoice_payments', [
			'invoice_id'     => $invoice->id,
			'amount'         => 300,
			'payment_method' => 1,
		]);
	}

	/**
	 ** @test
	 **
	 ** Rejecting a bank transfer for an invoice should update its
	 ** status to Rejected.
	 **/
	public function test_invoice_payment_rejection_marks_transfer_as_rejected()
	{
		$user    = User::factory()->create();
		$invoice = Invoice::factory()->create(['created_by' => $user?->id]);
		$transfer = InvoiceBankTransfer::factory()->create([
			'invoice_id' => $invoice->id,
			'amount'     => 200,
			'status'     => 'Pending',
		]);

		$this->actingAs($user);

		$response = $this->post(route('invoice.changeStatus', $invoice->id), [
			'order_id' => $transfer->order_id,
			'status'   => 'Rejected',
		]);

		$response->assertRedirect();
		$this->assertEquals('Rejected', $transfer->fresh()->status);
	}
}
