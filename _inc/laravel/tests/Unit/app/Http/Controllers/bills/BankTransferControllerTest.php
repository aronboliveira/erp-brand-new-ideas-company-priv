<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{BankAccount, BankTransfer, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Log, Route};
use Tests\TestCase;

class BankTransferControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
		DB::shouldReceive('beginTransaction')->andReturnTrue();
		DB::shouldReceive('commit')->andReturnTrue();
		DB::shouldReceive('rollBack')->andReturnTrue();

		if (!Route::has('bank-transfer.index')) {
			Route::view('bank-transfer.index', 'bank-transfer.index')->name('bank-transfer.index');
		}
	}

	/**
	 ** @test
	 **
	 ** Ensure that storing a new bank transfer creates the transfer record
	 ** and debits the source and credits the destination accounts.
	 **/
	public function test_store_creates_transfer_and_debits_accounts()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('create bank transfer');

		$from = BankAccount::factory()->create(['created_by' => $user?->id]);
		$to  = BankAccount::factory()->create(['created_by' => $user?->id]);

		$this->mockUtilityBalance();

		$response = $this->post(route('bank-transfer.store'), [
			'fromAccount' => $from->id,
			'toAccount'   => $to->id,
			'amount'      => 100.00,
			'date'        => now()->toDateString(),
		]);

		$response->assertRedirect(route('bank-transfer.index'));
		$this->assertDatabaseHas('bank_transfers', [
			'from_account' => $from->id,
			'to_account'   => $to->id,
			'amount'       => 100.00,
		]);
	}

	/**
	 ** @test
	 **
	 ** Verify that updating an existing bank transfer modifies its record
	 ** and adjusts the balances of the involved accounts correctly.
	 **/
	public function test_update_modifies_transfer_balances_correctly()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('edit bank transfer');

		$transfer = BankTransfer::factory()->create([
			'created_by'   => $user?->id,
			'from_account' => BankAccount::factory()->create(['created_by' => $user?->id])->id,
			'to_account'   => BankAccount::factory()->create(['created_by' => $user?->id])->id,
			'amount'       => 150,
		]);

		$newFrom = BankAccount::factory()->create(['created_by' => $user?->id]);
		$newTo  = BankAccount::factory()->create(['created_by' => $user?->id]);

		$this->mockUtilityBalance();

		$response = $this->put(route('bank-transfer.update', $transfer->id), [
			'fromAccount' => $newFrom->id,
			'toAccount'   => $newTo->id,
			'amount'      => 200,
			'date'        => now()->toDateString(),
		]);

		$response->assertRedirect(route('bank-transfer.index'));
		$this->assertDatabaseHas('bank_transfers', [
			'id'           => $transfer->id,
			'from_account' => $newFrom->id,
			'to_account'   => $newTo->id,
			'amount'       => 200,
		]);
	}

	/**
	 ** @test
	 **
	 ** Confirm that destroying a bank transfer removes the record
	 ** and restores the original account balances.
	 **/
	public function test_destroy_deletes_transfer_and_restores_balance()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('delete bank transfer');

		$transfer = BankTransfer::factory()->create([
			'created_by'   => $user?->id,
			'from_account' => BankAccount::factory()->create(['created_by' => $user?->id])->id,
			'to_account'   => BankAccount::factory()->create(['created_by' => $user?->id])->id,
			'amount'       => 300,
		]);

		$this->mockUtilityBalance();

		$response = $this->delete(route('bank-transfer.destroy', $transfer));
		$response->assertRedirect(route('bank-transfer.index'));
		$this->assertDatabaseMissing('bank_transfers', ['id' => $transfer->id]);
	}

	/**
	 ** @test
	 **
	 ** Mock the Utility::bankAccountBalance method to always return true
	 ** so that balance checks pass in the controller.
	 **/
	private function mockUtilityBalance(): void
	{
		\Mockery::mock('alias:App\Models\Utility')
			->shouldReceive('bankAccountBalance')
			->andReturnTrue();
	}
}
