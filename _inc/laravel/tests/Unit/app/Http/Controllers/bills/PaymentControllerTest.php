<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{User, Payment, Vendor, BankAccount, ProductServiceCategory};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PaymentControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected Vendor $vendor;
	protected BankAccount $account;
	protected ProductServiceCategory $category;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		$this->vendor = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$this->account = BankAccount::factory()->create(['created_by' => $this->user->creatorId()]);
		$this->category = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'expense'
		]);
	}

	/**
	 ** @test
	 **
	 ** index should display the payments index view
	 ** for the authenticated user.
	 **/
	public function test_index_returns_view(): void
	{
		$response = $this->get(route('payment.index'));
		$response->assertStatus(200)->assertViewIs('payment.index');
	}

	/**
	 ** @test
	 **
	 ** create should render the form for creating a new payment.
	 **/
	public function test_create_returns_form(): void
	{
		$response = $this->get(route('payment.create'));
		$response->assertStatus(200)->assertViewIs('payment.create');
	}

	/**
	 ** @test
	 **
	 ** store should persist a new payment record along with
	 ** saving the uploaded receipt file.
	 **/
	public function test_store_creates_payment(): void
	{
		Storage::fake('local');
		$receipt = UploadedFile::fake()->image('receipt.png');

		$response = $this->post(route('payment.store'), [
			'date'        => now()->toDateString(),
			'amount'      => 1500,
			'account_id'  => $this->account->id,
			'vendor_id'   => $this->vendor->id,
			'category_id' => $this->category->id,
			'add_receipt' => $receipt,
		]);

		$response->assertRedirect(route('payment.index'));
		$this->assertDatabaseHas('payments', [
			'vendor_id'  => $this->vendor->id,
			'account_id' => $this->account->id,
			'amount'     => 1500
		]);
	}

	/**
	 ** @test
	 **
	 ** edit should render the edit form for an existing payment
	 ** belonging to the authenticated user.
	 **/
	public function test_edit_returns_edit_view(): void
	{
		$payment = Payment::factory()->create([
			'created_by' => $this->user->creatorId()
		]);

		$response = $this->get(route('payment.edit', $payment));
		$response->assertStatus(200)->assertViewIs('payment.edit');
	}

	/**
	 ** @test
	 **
	 ** update should apply changes to the payment record and
	 ** redirect back to the payments index.
	 **/
	public function test_update_modifies_payment(): void
	{
		$payment = Payment::factory()->create([
			'created_by'  => $this->user->creatorId(),
			'vendor_id'   => $this->vendor->id,
			'account_id'  => $this->account->id,
			'category_id' => $this->category->id
		]);

		$response = $this->put(route('payment.update', $payment), [
			'date'        => now()->toDateString(),
			'amount'      => 3000,
			'account_id'  => $this->account->id,
			'vendor_id'   => $this->vendor->id,
			'category_id' => $this->category->id
		]);

		$response->assertRedirect(route('payment.index'));
		$this->assertDatabaseHas('payments', [
			'id'     => $payment->id,
			'amount' => 3000
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the specified payment record
	 ** and redirect to the payments index.
	 **/
	public function test_destroy_removes_payment(): void
	{
		$payment = Payment::factory()->create([
			'created_by' => $this->user->creatorId()
		]);

		$response = $this->delete(route('payment.destroy', $payment));
		$response->assertRedirect(route('payment.index'));
		$this->assertDatabaseMissing('payments', ['id' => $payment->id]);
	}
}
